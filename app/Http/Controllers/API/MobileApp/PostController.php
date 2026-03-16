<?php

namespace App\Http\Controllers\API\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \App\Models\PostReported;
use \App\Models\MobileAppUser;
use \App\Models\Followers;
use \App\Models\Post;
use App\Service\SearchHistoryService;
use App\Service\FirebaseService;
use Validator;
use Auth;
use App\Models\PostBookMark;
use Carbon\Carbon;
use Log;

class PostController extends Controller
{
    
    public function postCreateUpdate(Request $request){

        $validator=validator($request->all(),[
            'user_id'=>'required',
        ]);

        if ($validator->fails()) { 
            return [
                'success' => false, 
                'message' => $validator->errors()->first(),
            ];

        } else {

            try{

                $data=[
                    'user_id',
                    'user_name',
                    'field_id',
                    'post_type',
                    'location',
                    'date',
                    'description',
                    'thumbnail',
                    'auto_delete_date',
                    'status',
                    'title',
                    'rejected_reason',
                    'field_name',
                    'post_type_id',
                ];
                
                foreach ($data as $key => $value) {
                    if(isset($request[$value]) && $request[$value]!=null && $request[$value]!=''){
                        $obj[$value]=$request[$value];
                    }
                }

                if($request->id>0){

                    try{
                        $post=Post::findOrFail($request['id']);
                        $post->update($obj);

                    } catch (\Exception $e) {
                        return response(['error' => true, 'message' => 'Invalid Id to update'], 404); 
                    }
                }else{

                    $post=Post::create($obj);
                }

                return response()->json([
                    'status'=>200,
                    'message'=> $request->id>0 ? 'Post update successfully':'Post created successfully',
                    'post'=>$post
                ],200);
                
            } catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404); 
            }
        }
    }
                

    public function getPost(Request $request){
        $user=Auth::user();
        
        try{

            $updated = Post::where('is_disabled',0)
                ->where('auto_delete_date', '<', Carbon::now()->toDateTimeString())
                ->update(['is_disabled' => 1]); 

            // if($updated){

                $postList=Post::where('is_disabled',0)->with('user')->with('postType');
    
                $postList=$this->fetchuserbyquery($postList,$request,$user);
                $count=$postList->count();
                
                // $postList = $postList->orderby($sortBy,$sortOrder)
                $postList = $postList->orderby("id","desc")
                    ->skip($request['noofrec']*($request['currentpage']-1))->take($request['noofrec']??10)
                    ->get();

                // Get the bookmarked post IDs for the user 
                $bookmarkedPostIds = PostBookMark::where('user_id', $user['id'])->pluck('post_id')->toArray();

                // Add the 'is_saved' status to each post
                foreach ($postList as $post) {
                    $post['is_saved'] = in_array($post['id'], $bookmarkedPostIds) ? 1 : 0;
                }
            // }else{

            //     // Handle case where update fails
            //     return response()->json(['message' => 'Update failed'], 200);
            // }

            $response=[
                'postList'=>$postList,
                'count'=>$count,
                'message'=>'Successful',
                'post_event_updated'=>true
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response(['error' => true, 'message' => $e->getMessage()], 404); 
        }

    }
    
        // Filter Users
    public function fetchuserbyquery($postList, $request, $user)
    {
        if (!empty($request['search'])) {
            $keyword = "%" . $request['search'] . "%";
            $postList = $postList->where(function ($query) use ($keyword) {
                $query->where('user_name', 'like', $keyword)
                    ->orWhere('location', 'like', $keyword)
                    ->orWhere('status', 'like', $keyword)
                    ->orWhere('post_type', 'like', $keyword)
                    ->orWhere('title', 'like', $keyword)
                    ->orWhere('field_name', 'like', $keyword);
            });
    
            $search_history = new SearchHistoryService;
            $search_history->searchCreate($request['search'], $user);
        }
    
        if (!empty($request['status'])) {
            $postList = $postList->where('status', $request['status']);
        }
    
        if (!empty($request['post_type'])) {
            $postList = $postList->where('post_type', $request['post_type']);
        }
    
        if (!empty($request['location'])) {
            $postList = $postList->where('location', $request['location']);
        }
    
        if (!empty($request['field_id'])) {
            $postList = $postList->where('field_id', $request['field_id']);
        }
    
        if (!empty($request['field_name'])) {
            $postList = $postList->where('field_name', $request['field_name']);
        }
    
        if (!empty($request['date'])) {
            $postList = $postList->whereDate('date', $request['date']);
        }
    
        if (!empty($request['custom_date'])) {
            $postList = $postList->whereDate('date', $request['custom_date']);
        }
    
        if (!empty($request['fromDate']) && !empty($request['toDate'])) {
            $toDate = $request['toDate'] . ' 23:59:59';
            $postList = $postList->whereBetween('date', [$request['fromDate'], $toDate]);
        }
    
        return $postList;
    }
    

    // Count All Posts By Filter 
    public function fetchRecordsCount(Request $request){

        $now = Carbon::now();

        $updated = Post::where('is_disabled',0)->where('auto_delete_date', '<', Carbon::now()->toDateTimeString())
                ->update(['is_disabled' => 1]);

        //Counting ALL Posts by field
        $postsByField = Post::selectRaw('field_name,  MAX(id) as post_id ,COUNT(*) as total')
        ->groupBy('field_name')
        ->get();

        //Counting ALL POSTS by post type
        $postsByType = Post::selectRaw('post_type, MAX(id) as post_id, COUNT(*) as total')
        ->groupBy('post_type')
        ->get();

        // Today
        $todayCount = Post::whereDate('created_at', $now->toDateString())->count();

        // Tomorrow
        $tomorrowCount = Post::whereDate('created_at', $now->addDay()->toDateString())->count();

        // This Week
        $thisWeekCount = Post::whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()])->count();

        // Last Week
        $lastWeekStart = $now->subWeek()->startOfWeek();
        $lastWeekEnd = $now->endOfWeek();
        $lastWeekCount = Post::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        // This Month
        $thisMonthCount = Post::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Custom Range (Example: Start and End Dates)
        $startDate = $request['start_date'];
        $endDate = $request->input('end_date', Carbon::now()->toDateString()); // Default to today's date

        $customRangeCount =[];
        if(isset($startDate) && $startDate!=null && $startDate!=''){
            $customRangeCount = Post::whereBetween('created_at', [$startDate, $endDate])->count();
        }

        $postsByDate=[
            'today' => $todayCount,
            'tomorrow' => $tomorrowCount,
            'this_week' => $thisWeekCount,
            'last_week' => $lastWeekCount,
            'this_month' => $thisMonthCount,
            'custom_range' => $customRangeCount
        ];

        $postsByCreator=Post::selectRaw('mobile_app_users.user_type, COUNT(posts.id) as total_posts')
            ->join('mobile_app_users', 'mobile_app_users.id', '=', 'posts.user_id') // Join the users table
            ->groupBy('mobile_app_users.user_type') // Group by user_type
            ->get();

        $response=[
            'success'=>true,
            'message'=>'Total count filter posts fetched',
            'posts_by_field' => $postsByField,
            'posts_by_type' => $postsByType,
            'posts_by_date'=>$postsByDate,
            'posts_by_creator'=>$postsByCreator
        ];

        return response()->json($response);

    }


    public function deletePost(Request $request){

        $validator=validator($request->all(),[
           'id'=>'required'
        ]);

       if ($validator->fails()) { 
           return [
               'success' => false, 
               'message' => $validator->errors()->first(),
           ];

       } else {

           try{
                $post =  Post::where('id',$request->input('id'))->first();

                $post['is_disabled']=1;
                $post->update();

                // Send the Device Notification
                $firebase_notify = new FirebaseService;

                $user = MobileAppUser::where('id',$post->user_id)->first();

                $deviceToken = $user->fcm_token;

                if($deviceToken){
                    $title = 'Post Deleted';
                    $body = 'Your post '.$post->title.' has been deleted ';

                    $firebase_notify->createNotification($user['id'],$title,$body);
                    $notification=$firebase_notify->sendNotification($deviceToken, $title, $body);
                }else{
                    $notification=[
                        'success'=>false,
                        'message'=>'Device Token not found'
                    ];
                }

                Log::info("notification Post Deleted");
                Log::info($notification);

                $response=[
                    'success'=>true,
                    'notification'=>$notification,
                    'message'=>'Post Deleted successfully.'
                ];

                return response()->json($response);

            } catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404); 
            }
       }
    }

    
    public function updateStatus(Request $request){
        
        $validator=validator($request->all(),[
            'id'=>'required' 
         ]);
 
        if ($validator->fails()) { 
            return [
                'success' => false, 
                'message' => $validator->errors()->first(),
            ];
 
        } else {

            try{
                
                $update = Post::where('id',$request->input('id'))->first();
                $update['status']=$request['status'];
                $update->update();

                // Send the Device Notification
                $firebase_notify = new FirebaseService;

                $user = MobileAppUser::where('id',$update->user_id)->first();

                $deviceToken = $user->fcm_token;

                if($deviceToken){
                    $title = 'Post Status Updated';
                    $body = 'Your post '.$update->title.' has been '.$request['status'];

                    $firebase_notify->createNotification($user['id'],$title,$body);
                    $notification=$firebase_notify->sendNotification($deviceToken, $title, $body);
                }else{
                    $notification=[
                        'success'=>false,
                        'message'=>'Device Token not found'
                    ];
                }

                Log::info("notification Post Status Updated");
                Log::info($notification);
 

                $response=[
                    'success'=>true,
                    'notification'=>$notification,
                    'message'=>'Status Updated successfully.',
                    'result'=>$update
                ];

                return response()->json($response);

            } catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404); 
            }
        }
    }

    public function postDetails(Request $request)
    {
        $user=Auth::user();

        $validator = validator($request->all(), [
            'id' => 'required'
        ]);
    
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            try {
                // Fetch the post details along with related user and postType
                $post = Post::where('id', $request->input('id'))
                    ->with(['user', 'postType'])
                    ->first();
    
                if (!$post) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Post not found.',
                    ], 404);
                }
    
                // Determine if the current user follows the post's owner
                $isFollowing = Followers::where('user_id', $user->id)
                    ->where('follower_id', $post->user->id)
                    ->exists();
    
                // Add the isFollowing attribute to the user object
                $post->user->is_following = $isFollowing;
    
                return response()->json([
                    'success' => true,
                    'message' => 'Post retrieved successfully.',
                    'result' => $post,
                ]);
    
            } catch (\Exception $e) {
                return response()->json([
                    'error' => true,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }
    }
    

    public function postDetailsUser(Request $request)
    {
        $validator = validator($request->all(), [
            'user_id' => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            try {

                $updated = Post::where('is_disabled',0)->where('auto_delete_date', '<', Carbon::now()->toDateTimeString())
                ->update(['is_disabled' => 1]);

                // Fetch the posts
                $postList = Post::where('is_disabled', 0)
                    ->where('user_id', $request->input('user_id'))
                    ->with(['user', 'postType']);
    
                $postList = $this->fetchuserbyquerydetails($postList, $request);
                $count = $postList->count();
    
                $postList = $postList->orderby('id', 'desc')
                    ->skip($request['noofrec'] * ($request['currentpage'] - 1))
                    ->take($request['noofrec'] ?? 100)
                    ->get();
    
                // Add `is_following` to each user's details
                foreach ($postList as $post) {
                    $post->user->is_following = Followers::where('user_id', $post->user->id)
                        ->where('follower_id', $request->input('user_id'))
                        ->exists();
                }
    
                // Fetch total followers and following count
                $totalFollowersCount = Followers::where('user_id', $request->input('user_id'))->count();
                $totalFollowingCount = Followers::where('follower_id', $request->input('user_id'))->count();
    
                $response = [
                    'success' => true,
                    'message' => 'Post get successfully.',
                    'result' => $postList,
                    'totalPostCount' => $count,
                    'totalfollowersCount' => $totalFollowersCount,
                    'totalfollowingCount' => $totalFollowingCount,
                ];
    
                return response()->json($response);
    
            } catch (\Exception $e) {
                return response(['error' => true, 'message' => $e->getMessage()], 404);
            }
        }
    }
    

    // Filter Users
    public function fetchuserbyquerydetails($postList,$request){

        if(isset($request['search']) && $request['search']!='') {
            $keyword = "%".$request['search']."%";
            $postList = $postList->whereRaw(" 
            (
            user_name like ? 
            or location like ?
            or status like ?
            or post_type like ?
            or title like ?
            or field_name like ?
            ) ", 
            array(
                $keyword, 
                $keyword,
                $keyword,
                $keyword,
                $keyword,
                $keyword,
            ));
        }

        if ($request['status']!='' && isset($request['status'])) {
            $status = $request->status;
            $postList = $postList->where('status', $status);
        }

        if ($request['post_type']!='' && isset($request['post_type'])) {
            $post_type = $request->post_type;
            $postList = $postList->where('post_type', $post_type);
        }

        if ($request['location']!='' && isset($request['location'])) {
            $location = $request->location;
            $postList = $postList->where('location', $location);
        }

        if ($request['field_id']!='' && isset($request['field_id'])) {
            $field_id = $request->field_id;
            $postList = $postList->where('field_id', $field_id);
        }

        if ($request['field_name'] != '' && isset($request['field_name'])) {
            $field_name = $request->field_name;
            $postList = $postList->where('field_name', $field_name);
        }
        // get custom_date data by date
        if ($request['date']!='' && isset($request['date'])) {
            $date = $request->date;
            $postList = $postList->whereDate('date', $date);
        }

        if ($request['custom_date']!='' && isset($request['custom_date'])) {
            $customDate = $request->custom_date;
            $postList = $postList->whereDate('date', $customDate);
        }

        // get fromDate toDate data by created_at
        if ($request['fromDate']!='' && isset($request['toDate'])) {
            $fromDate = $request->fromDate;
            $toDate = $request->toDate;
        
            // 'fromDate' and 'toDate'
            $postList = $postList->whereDate('date', '>=', $fromDate)->whereDate('date', '<=', $toDate);
        }

        return $postList;
    }
    // post reported

    public function post_reportedsCreateUpdate(Request $request)
    {
    
        try {
            $data = [
                'user_id' => $request->user_id,
                'user_name' => $request->user_name ?? null,
                'post_id' => $request->post_id,
                'post_title' => $request->post_title ?? null,
                'reason' => $request->reason,
                'date' => $request->date ?? now()
            ];

            $update_post = Post::where('id', $request->post_id)
                ->first();

            if (!$update_post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found or unauthorized action.',
                ], 404);
            }

            $update_post->status = "Reported";
            $update_post->save();

            if ($request->id > 0) {
                $post_reporteds = PostReported::find($request->id);

                if (!$post_reporteds) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid ID to update.',
                    ], 404);
                }

                $post_reporteds->update($data);
                $message = 'Post Reported updated successfully.';
            } else {
                $post_reporteds = PostReported::create($data);
                $message = 'Post Reported successfully.';
            }

            return response()->json([
                'status' => 200,
                'message' => $message,
                'post_reported' => $post_reporteds,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }           

    public function getPost_reporteds(Request $request){

        try{

            $post_reportedsList=PostReported::with('user');

            if(isset($request['search']) && $request['search']!='') {
                $keyword = "%".$request['search']."%";
                $post_reportedsList = $post_reportedsList->whereRaw(" 
                (
                user_name like ? 
                or post_id like ?
                or post_title like ?
                or reason like ?
                ) ", 
                array(
                    $keyword , 
                    $keyword,
                    $keyword,
                    $keyword,
                ));
            }

            if ($request['status']!='' && isset($request['status'])) {
                $status = $request->status;
                $post_reportedsList = $post_reportedsList->where('status', $status);
            }

            if ($request['date']!='' && isset($request['date'])) {
                $date = $request->date;
                $post_reportedsList = $post_reportedsList->whereDate('date', $date);
            }

            if ($request['custom_date']!='' && isset($request['custom_date'])) {
                $customDate = $request->custom_date;
                $post_reportedsList = $post_reportedsList->whereDate('date', $customDate);
            }

            // get fromDate toDate data by created_at
            if ($request['fromDate']!='' && isset($request['toDate'])) {
                $fromDate = $request->fromDate;
                $toDate = $request->toDate;
                $toDate .= ($fromDate === $toDate) ? ' 23:59:59' : ' 23:59:59';
                // 'fromDate' and 'toDate'
                $post_reportedsList = $post_reportedsList->whereDate('date', '>=', $fromDate)
                ->whereDate('date', '<=', $toDate);
            }
            
            $count=$post_reportedsList->count();
            $post_reportedsList = $post_reportedsList->orderby("id","desc")
                ->skip($request['noofrec']*($request['currentpage']-1))->take($request['noofrec']??100)
                ->get();

            $response=[
                'post_reportedsList'=>$post_reportedsList,
                'count'=>$count,
                'message'=>'Successful',
            ];

            return response()->json($response);
        
        } catch (\Exception $e) {
            return response(['error' => true, 'message' => $e->getMessage()], 404); 
        }
    }


    // Filter by
    public function postFilterby(Request $request)
    {

        // Auto Delete Post
        $updated = Post::where('is_disabled',0)->where('auto_delete_date', '<', Carbon::now()->toDateTimeString())
                ->update(['is_disabled' => 1]);

        $postList = Post::where('is_disabled', 0);
    
        $status_count = 0;
        $post_count = 0;
        $location_count = 0;
        $field_count = 0;
        $date_count = 0;
        $created_count = 0;
    
        // Filter by status
        if (!empty($request->status)) {
            $postList = $postList->where('status', $request->status);
            $status_count = Post::where('status', $request->status)->count();
        }
    
        // Filter by post_type
        if (!empty($request->post_type)) {
            $postList = $postList->where('post_type', $request->post_type);
            $post_count = Post::where('post_type', $request->post_type)->count();
        }
    
        // Filter by location
        if (!empty($request->location)) {
            $postList = $postList->where('location', $request->location);
            $location_count = Post::where('location', $request->location)->count();
        }
    
        // Filter by field_name
        if (!empty($request->field_name)) {
            $postList = $postList->where('field_name', $request->field_name);
            $field_count = Post::where('field_name', $request->field_name)->count();
        }
    
        // Filter by exact date
        if (!empty($request->date)) {
            $postList = $postList->whereDate('date', $request->date);
            $date_count = Post::whereDate('date', $request->date)->count();
        }
    
        // Filter by custom date (created_at)
        if (!empty($request->custom_date)) {
            $postList = $postList->whereDate('created_at', $request->custom_date);
            $created_count = Post::whereDate('created_at', $request->custom_date)->count();
        }
    
        // Pagination and Sorting
        $postList = $postList->orderby('id', 'desc')
            ->skip(($request->noofrec ?? 100) * (($request->currentpage ?? 1) - 1))
            ->take($request->noofrec ?? 100)
            ->get();
    
        $response = [
            'postList' => $postList,
            'total_count' => Post::where('is_disabled', 0)->count(),
            'status_count' => $status_count,
            'post_count' => $post_count,
            'location_count' => $location_count,
            'field_count' => $field_count,
            'date_count' => $date_count,
            'created_count' => $created_count,
            'message' => 'Successful',
        ];
    
        return response()->json($response, 200);
    }
    
    public function postOverView(Request $request)
    {
    
        try {
                $post_overview=[];

                $postList=Post::where('is_disabled',0);
                $user_type=MobileAppUser::where('is_disabled',0)->where('login_type','User');
                
                // $post_reportedsList=PostReported::with('user');

                $total_post = (clone $postList)->count();
                $approved_count = (clone $postList)->where('status', 'Approved')->count();
                $pending_count = (clone $postList)->where('status', 'Pending')->count();
                $reject_count = (clone $postList)->where('status', 'Reported')->count();
                $reported_count = (clone $postList)->where('status', 'Reported')->count();

                $business_count = (clone $user_type)->where('user_type', 'Business')->count();
                $individual_count = (clone $user_type)->where('user_type', 'Individual')->count();


                $post_overview= [
                    'total_post' => $total_post,
                    'approved_count' => $approved_count,
                    'pending_count' =>  $pending_count,
                    'reject_count' => $reject_count,
                    'reported_count' => $reported_count,
                ];

            $response = [
                'success' => true,
                'post_overview' => $post_overview,
                'business_count' => $business_count,
                'individual_count' => $individual_count,
            ];
    
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 404);
        }
    }

    // Add Bookmark
    public function addBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            // Check if already bookmarked
            $existingBookmark = PostBookMark::where('user_id', $request->user_id)
                ->where('post_id', $request->post_id)
                ->first();

            if ($existingBookmark) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already bookmarked',
                ], 200);
            }

            // Add Bookmark
            PostBookMark::create([
                'user_id' => $request->user_id,
                'post_id' => $request->post_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bookmark added successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Remove Bookmark
    public function removeBookmark(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'post_id' => 'required|exists:posts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $bookmark = PostBookMark::where('user_id', $request->user_id)
                ->where('post_id', $request->post_id)
                ->first();

            if (!$bookmark) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bookmark not found',
                ], 404);
            }

            $bookmark->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bookmark removed successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Fetch Bookmarked Posts
    public function fetchBookmarks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 400);
        }

        try {
            $bookmarks = PostBookMark::where('user_id', $request->user_id)
                ->with('post','post.user'); // Ensure that `post` is a valid relationship in the PostBookMark model

            $count=$bookmarks->count();

            $bookmarks=$bookmarks->orderby("id","desc")
                ->skip($request['noofrec']*($request['currentpage']-1))->take($request['noofrec']??10)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Bookmarks fetched successfully',
                'count'=>$count,
                'data' => $bookmarks,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
