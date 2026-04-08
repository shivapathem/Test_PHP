USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_PublishAllocations]    Script Date: 30/03/2026 15:21:14 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER                  PROCEDURE [dbo].[usp_mod_PublishAllocations]
@weekNumber				INT,
@teamId					INT,
@isPublished			INT,
@futureAdhocDuty		INT,
@IsIncrementalPublish   INT,
@pNetLogin				VARCHAR(30)

AS
BEGIN

   SET NOCOUNT ON;

   SET DATEFORMAT YMD

   DECLARE  @vuserID                  INT,
			@vname                    VARCHAR(50),
			@vIsPublished             INT,
			@vAllocationID            INT,
			@HistoryTypeID            INT,
			@History                  VARCHAR(MAX);

   DECLARE @TempDutyList Table (AllocationsSPID INT,
								AllocationsDutyID INT,
								IsHomeTeam		  BIT)

    SELECT @vname =  UD_DisplayName,
		   @vuserID = UD_UserID
	  FROM UserDetails (nolock)
	 WHERE UD_NetLogin = @pNetLogin

    BEGIN TRY
        BEGIN TRANSACTION


	               SELECT @vIsPublished = AL_Status,
				          @vAllocationID = AL_AllocationsID
                     FROM Allocations
                    WHERE AL_WeekNumber = @weekNumber
                      and AL_SchedulingTeamID = @teamId ;

					SELECT @HistoryTypeID = ID
					  FROM HistoryTypes
					 WHERE HistoryType = 'PublishWeek'

        IF ( ( @isPublished = 0 AND @vIsPublished = 1 )
				OR ( @isPublished = 1 AND @vIsPublished = 1 AND ISNULL(@IsIncrementalPublish,0) = 0) )
            BEGIN

                DELETE AJP
                  FROM Allocations_Jobs_Publish AJP
                 INNER JOIN Allocations_Publish AP ON AP.ID=AJP.allocationid
                 WHERE AP.AllocationID = @vAllocationID

                DELETE AP
                  FROM Allocations_Publish AP
                 WHERE AP.AllocationID = @vAllocationID

			  DELETE APS
				FROM   Allocations AS AL
				INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
				INNER JOIN AllocationsAddPersons ADP on ADP.AAP_AllocationsSPID = ASP_AllocationsSPID
				INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				INNER JOIN Allocations AP on AP.AL_AllocationsID = AAP_AllocationsID
				INNER JOIN ScheduledPersonTeam_LINK SPL on SPL.TeamID = AP.AL_SchedulingTeamID
														and SPL.ScheduledPersonID = ASP_SchedulingPersonID
				INNER JOIN Allocations_Publish APS on AP.AL_AllocationsID = APS.AllocationID
												  AND ASP_AllocationsSPID = APS.AllocationsSPID
				WHERE SPL.scheduledType = 1
				  AND AD_DutyDate between SpL.startdate AND SpL.enddate
				  AND AL.AL_AllocationsID = @vAllocationID

			END

        IF ( @isPublished = 0 AND @vIsPublished = 1 )
            BEGIN

                UPDATE Allocations
                   SET AL_Status = 0,
                       AL_UpdatedBy = @vuserID,
                       AL_UpdatedDate = getdate()
                WHERE AL_AllocationsID = @vAllocationID

			   SET @History = 'The published allocations for '+RIGHT(cast(@weekNumber as VARCHAR),2) + '/'
				             + LEFT(cast(@weekNumber as VARCHAR),len(cast(@weekNumber as VARCHAR))-2)
							 +' were deleted on '
							 + FORMAT(Getdate(),'dd/MM/yyyy') + ' at '
							 + FORMAT(Getdate(),'HH:mm')
							 + ' by '
							 + @vname

				EXEC [usp_mod_AllocationHistory] @vAllocationid,@HistoryTypeID,@vuserID,@History,1;

            END

		IF (@isPublished = 1 AND @futureAdhocDuty = 1)
		 BEGIN
		    UPDATE MasterDuties
			   SET isPublished = 1
			 WHERE TeamID = 103
			   AND DutyTypeID = 6
			   AND StartWeek > @weekNumber
		 END

        IF ( ( @isPublished = 1 AND @vIsPublished = 0 )
		      OR ( @isPublished = 1 AND @vIsPublished = 1 AND ISNULL(@IsIncrementalPublish,0) = 0) )
          BEGIN

            INSERT INTO Allocations_Publish
                        (allocationid,
                         dutyname,
                         duration,
                         weeknumber,
                         iday,
                         starttime,
                         endtime,
                         sortcode,
                         leaveid,
                         dutycomments,
                         backcolour,
                         fontcolour,
                         personcomments,
                         adhocduty,
                         markedovertime,
                         markedptextraday,
                         markedcompleave,
                         markedsickness,
                         manualotamount,
                         manualotexcbreaksamount,
                         unallocated,
                         schedulingteamid,
                         schedulingpersonid,
                         dutydate,
                         startdate,
                         enddate,
                         isattention,
                         isrequest,
                         aftermidnight,
                         dutyprogramid,
						 DutyProgramId2,
						 DutyProgramId3,
						 DutyProgramId4,
						 DutyProgramId5,
						 DutyProgramId6,
                         dutybreaktime,
                         dutycolorid,
                         ispublished,
                         ishometeam,
                         markwiad,
                         markactual,
                         isedited,
                         mannualothours,
                         isactive,
                         iseditable,
                         origallocationid,
                         masterdutyid,
                         isactiveduty,
						 DutyTeamID,
						 LeaveStartTime,
						 LeaveEndTime,
						 AllocationsDutyID,
						 AllocationsSPID,
						 SigninStatus,
						 SigninStartTime,
						 SigninEndTime,
						 SigninINBuilding)
            SELECT al.AL_AllocationsID,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 'U' 
						ELSE CASE WHEN AD_DutyType IN ( 8,11,12)
								 THEN CASE WHEN ASP_LeaveType = 1
										   THEN 'Leave'
										   WHEN ASP_LeaveType = 2
										   THEN 'OFF Leave'
										   WHEN ASP_LeaveType = 3
										   THEN 'Sick'
										   WHEN ASP_LeaveType = 4
										   THEN 'U-Sick'
										   WHEN ASP_LeaveType = 5
										   THEN '-Sick'
										   WHEN ASP_LeaveType = 7
										   THEN 'Absent'
									   END
							ELSE AD_DutyName END			
						END AS dutyname,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE 
									CASE WHEN AD_DutyType IN ( 8,11,12)
										 THEN ASP_LeaveDuration
										 ELSE AD_Duration 
									END 
				   END AS duration,
                   AL.AL_WeekNumber,
                   ASP_iDay,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_StartTimeSec END AS starttime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_EndTimeSec END AS endtime,
                   ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   ASP_Comments as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   ASP_MarkedOverTime markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   case when ASP_LeaveType in (3,4,5) then 1 else 0 end as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   al.AL_SchedulingTeamID,
                   ASP_SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyBreakTime END AS dutybreaktime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 
						ELSE CASE WHEN AD_DutyType IN ( 8,11,12)
								 THEN ASP_LeaveColourID
								 ELSE AD.AD_DutyColourID END
						END AS dutycolorid,
                   1 as ispublished,
                   ishometeam,
                   case when ASP_WIADStatus = 1 then 1 end as markwiad,
                   case when ASP_WIADStatus = 2 then 1 end as markactual,
                   0 isedited,
                   ASP_OverTimeHours mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   ASP_DutyTeamID,
				   ASP_LeaveStartTimeSec,
				   ASP_LeaveEndTimeSec,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   ASP_SigninStatus,
				   ASP_SigninStartTime,
				   ASP_SigninEndTime,
				   ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
			INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
													and spl.ScheduledPersonID = ASP_SchedulingPersonID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN Allocations AP on AP.AL_AllocationsID = AD.AD_AllocationsID
			WHERE SPL.scheduledType = 1
			  AND AD_DutyDate between SpL.startdate AND SpL.enddate
			  AND AL.AL_AllocationsID = @vAllocationID
			  UNION ALL
            SELECT al.AL_AllocationsID,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 'U' 
						ELSE CASE WHEN AD_DutyType IN ( 8,11,12)
								 THEN CASE WHEN ASP_LeaveType = 1
										   THEN 'Leave'
										   WHEN ASP_LeaveType = 2
										   THEN 'OFF Leave'
										   WHEN ASP_LeaveType = 3
										   THEN 'Sick'
										   WHEN ASP_LeaveType = 4
										   THEN 'U-Sick'
										   WHEN ASP_LeaveType = 5
										   THEN '-Sick'
										   WHEN ASP_LeaveType = 7
										   THEN 'Absent'
									   END
							ELSE AD_DutyName END			
						END AS dutyname,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE 
									CASE WHEN AD_DutyType IN ( 8,11,12)
										 THEN ASP_LeaveDuration
										 ELSE AD_Duration 
									END 
				   END AS duration,
                   AL.AL_WeekNumber,
                   ASP_iDay,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_StartTimeSec END AS starttime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_EndTimeSec END AS endtime,
                   ADP.AAP_SortCode ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   ADP.AAP_Comments as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   ASP_MarkedOverTime markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   case when ASP_LeaveType in (3,4,5) then 1 else 0 end as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   al.AL_SchedulingTeamID,
                   ASP_SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyBreakTime END AS dutybreaktime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 
						ELSE CASE WHEN AD_DutyType IN ( 8,11,12)
								 THEN ASP_LeaveColourID
								 ELSE AD.AD_DutyColourID END END AS dutycolorid,
                   1 as ispublished,
                   ishometeam,
                   case when ASP_WIADStatus = 1 then 1 end as markwiad,
                   case when ASP_WIADStatus = 2 then 1 end as markactual,
                   0 isedited,
                   ASP_OverTimeHours mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   ASP_DutyTeamID,
				   ASP_LeaveStartTimeSec,
				   ASP_LeaveEndTimeSec,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   ASP_SigninStatus,
				   ASP_SigninStartTime,
				   ASP_SigninEndTime,
				   ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN AllocationsAddPersons ADP on AL_AllocationsID = ADP.AAP_AllocationsID
			INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
			INNER JOIN AllocationsScheduledPersons ASP on ADP.AAP_AllocationsSPID = ASP_AllocationsSPID
													and spl.ScheduledPersonID = ASP_SchedulingPersonID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN Allocations AP on AP.AL_AllocationsID = AD.AD_AllocationsID
			WHERE SPL.scheduledType = 1
			  AND spl.IsHomeTeam IN (0,2)
			  AND AD_DutyDate between SpL.startdate AND SpL.enddate
			  AND AL.AL_AllocationsID = @vAllocationID
			  UNION ALL
		   SELECT al.AL_AllocationsID,
                   AD_DutyName,
                   AD_Duration AS duration,
                   AL.AL_WeekNumber,
                   AD_iDay,
                   AD_StartTimeSec  AS starttime,
                   AD_EndTimeSec  AS endtime,
                   NULL AS ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   NULL as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   0 markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   0 as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   al.AL_SchedulingTeamID,
                   0 As SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   AD_DutyBreakTime  AS dutybreaktime,
                   AD_DutyColourID  AS dutycolorid,
                   1 as ispublished,
                   1 as ishometeam,
                   0 as markwiad,
                   0 as markactual,
                   0 as isedited,
                   0 as  mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   0 as DutyTeamID,
				   0 as LeaveStartTimeSec,
				   0 as LeaveEndTimeSec,
				   AD_AllocationsDutyID as AllocationsDutyID,
				   0 as AllocationsSPID,
				   0 as ASP_SigninStatus,
				   0 as ASP_SigninStartTime,
				   0 as ASP_SigninEndTime,
				   0 as ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID
			WHERE  AL.AL_AllocationsID = @vAllocationID
			  AND AD_DutyStatus = 0
			  AND AD_DutyType < 7
			  UNION ALL
            SELECT ap.AL_AllocationsID,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 'U' 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN CASE WHEN ASP_LeaveType = 1
										   THEN 'Leave'
										   WHEN ASP_LeaveType = 2
										   THEN 'OFF Leave'
										   WHEN ASP_LeaveType = 3
										   THEN 'Sick'
										   WHEN ASP_LeaveType = 4
										   THEN 'U-Sick'
										   WHEN ASP_LeaveType = 5
										   THEN '-Sick'
										   WHEN ASP_LeaveType = 7
										   THEN 'Absent'
									   END
							ELSE AD_DutyName END			
						END AS dutyname,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE 
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveDuration
										 ELSE AD_Duration 
									END 
				   END AS duration,
                   AL.AL_WeekNumber,
                   ASP_iDay,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_StartTimeSec END AS starttime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_EndTimeSec END AS endtime,
                   ADP.AAP_SortCode ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   ADP.AAP_Comments as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   ASP_MarkedOverTime markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   case when ASP_LeaveType in (3,4,5) then 1 else 0 end as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   ap.AL_SchedulingTeamID,
                   ASP_SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyBreakTime END AS dutybreaktime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyColourID END AS dutycolorid,
                   1 as ispublished,
                   ishometeam,
                   case when ASP_WIADStatus = 1 then 1 end as markwiad,
                   case when ASP_WIADStatus = 2 then 1 end as markactual,
                   0 isedited,
                   ASP_OverTimeHours mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   ASP_DutyTeamID,
				   ASP_LeaveStartTimeSec,
				   ASP_LeaveEndTimeSec,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   ASP_SigninStatus,
				   ASP_SigninStartTime,
				   ASP_SigninEndTime,
				   ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
			INNER JOIN AllocationsAddPersons ADP on ADP.AAP_AllocationsSPID = ASP_AllocationsSPID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN Allocations AP on AP.AL_AllocationsID = AAP_AllocationsID
			INNER JOIN ScheduledPersonTeam_LINK SPL on SPL.TeamID = AP.AL_SchedulingTeamID
													and SPL.ScheduledPersonID = ASP_SchedulingPersonID
			WHERE SPL.scheduledType = 1
			  AND SPL.IsHomeTeam IN (0,2)
			  AND AD_DutyDate between SpL.startdate AND SpL.enddate
			  AND AL.AL_AllocationsID = @vAllocationID

        INSERT INTO [dbo].[Allocations_Jobs_Publish]
                    (allocateinstanceid,
                     departmentid,
                     allocationid,
                     allocatejobid,
                     staffnumber,
                     weeknumber,
                     iday,
                     programme,
                     contact,
                     location,
                     jobname,
                     starttime,
                     endtime,
                     jobbackcolour,
                     jobfontcolour,
                     comments,
                     masterjobid,
                     adhocduty,
                     unallocated,
                     schedulingpersonid,
                     programmeid,
                     jobdefaultcolour,
                     aftermidnight,
                     schedulingteamid,
                     edited,
                     ispublished,
                     isedited,
                     isactive,
                     job_info)
            SELECT 0 allocateinstanceid,
                   0 departmentid,
                   AP.ID,
                   AJ.AJ_AllocateJobID,
                   null as staffnumber,
                   0 as weeknumber,
                   0 as iday,
                   pg.programme,
                   AJ.AJ_Contact as contact,
                   AJ.AJ_Location,
                   AJ_JobName,
                   AJ_JobStartTimeSec,
                   AJ_JobEndTimeSec,
                   AJ_JobBGColour jobbackcolour,
                   AJ_JobFontColour jobfontcolour,
                   AJ_Comments comments,
                   AJ_MasterJobID as masterjobid,
                   0 adhocduty,
                   case when AJ_JobStatus = 0 then 1 else 0 end as unallocated,
                   0 schedulingpersonid,
                   AJ_ProgrammeID,
                   NULL as jobdefaultcolour,
                   case when DATEDIFF(DAY,AJ_JobStartTimeLocal,AJ_JobEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   0 as schedulingteamid,
                   0 as edited,
                   1 as ispublished,
                   0 as isedited,
                   case when AJ_JobStatus in (0,1) then 1
					    when AJ_JobStatus = 9 then 0
						else 1 end as isactive,
                   AJ_JobInfo
             FROM AllocationsJobs AJ
            INNER JOIN AllocationsDuties AD on AD.AD_AllocationsDutyID = AJ.AJ_AllocationsDutyID
            INNER JOIN Allocations_Publish AP ON AD.AD_AllocationsID = AP.allocationid AND AD.AD_AllocationsDutyID = AP.AllocationsDutyID
			LEFT join Programmes pg on pg.ID = aj.AJ_ProgrammeID
            WHERE AD_AllocationsID = @vAllocationID

                UPDATE Allocations
                   SET AL_Status = 1,
                       AL_UpdatedBy = @vuserID ,
                       AL_UpdatedDate = GETDATE()
                WHERE AL_AllocationsID = @vAllocationID

				UPDATE AU
				   SET AU.AU_Status = 1
				  FROM AllocationsUpdated AU
				 INNER JOIN AllocationsScheduledPersons ASP ON ASP.ASP_AllocationsSPID = AU.AU_AllocationsSPID
				 INNER JOIN Allocations AL ON AL.AL_AllocationsID = ASP_AllocationsID
				 WHERE AL_AllocationsID = @vAllocationID
				   AND AU_STATUS = 0

				UPDATE AU
				   SET AU.AU_Status = 1
				  FROM AllocationsUpdated AU
				 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = AU_AllocationsDutyID
				 INNER JOIN Allocations AL ON AL.AL_AllocationsID = AD_AllocationsID
				 WHERE AL_AllocationsID = @vAllocationID
				   AND AU_STATUS = 0

				SET @History = 'Week '+RIGHT(cast(@weekNumber as VARCHAR),2) + '/'
				               + LEFT(cast(@weekNumber as VARCHAR),len(cast(@weekNumber as VARCHAR))-2)
							   +' was published on '
							   + FORMAT(Getdate(),'dd/MM/yyyy') + ' at '
							   + FORMAT(Getdate(),'HH:mm')
							   + ' by '
							   + @vname

				EXEC [usp_mod_AllocationHistory] @vAllocationid,@HistoryTypeID,@vuserID,@History,1;


        END

        IF ( @isPublished = 1 AND  @vIsPublished = 1 AND @IsIncrementalPublish = 1 )
          BEGIN

		       INSERT INTO @TempDutyList(AllocationsSPID, AllocationsDutyID, IsHomeTeam)
			    SELECT  ASP_AllocationsSPID, ASP_AllocationsDutyID,1
				  FROM AllocationsUpdated AU
				 INNER JOIN AllocationsScheduledPersons ASP ON ASP.ASP_AllocationsSPID = AU.AU_AllocationsSPID
				 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				 INNER JOIN Allocations AL ON AL.AL_AllocationsID = ASP_AllocationsID
				 WHERE AL_AllocationsID = @vAllocationID
				   AND AU_STATUS = 0
				 GROUP BY ASP_AllocationsSPID, ASP_AllocationsDutyID

		       INSERT INTO @TempDutyList(AllocationsDutyID,IsHomeTeam)
			    SELECT  DISTINCT AD_AllocationsDutyID, 1
				  FROM AllocationsUpdated AU
				 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = AU_AllocationsDutyID
				 INNER JOIN Allocations AL ON AL.AL_AllocationsID = AD_AllocationsID
				 WHERE AL_AllocationsID = @vAllocationID
				   AND AU_STATUS = 0
				   AND NOT EXISTS ( SELECT 1
									  FROM @TempDutyList TL
									 WHERE TL.AllocationsDutyID = AU_AllocationsDutyID )

		       INSERT INTO @TempDutyList(AllocationsSPID, AllocationsDutyID, IsHomeTeam)
			    SELECT  ASP_AllocationsSPID, ASP_AllocationsDutyID,0
				  FROM AllocationsUpdated AU
				 INNER JOIN AllocationsAddPersons AAP ON AAP.AAP_AllocationsSPID = AU.AU_AllocationsSPID
				 INNER JOIN AllocationsScheduledPersons ASP ON ASP.ASP_AllocationsSPID = AU.AU_AllocationsSPID
				 INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
				 INNER JOIN Allocations AL ON AL.AL_AllocationsID = AAP_AllocationsID
				 WHERE AL_AllocationsID = @vAllocationID
				   AND AU_STATUS = 0
				 GROUP BY ASP_AllocationsSPID, ASP_AllocationsDutyID

		       INSERT INTO @TempDutyList(AllocationsDutyID,IsHomeTeam)
			    SELECT  DISTINCT AU_AllocationsDutyID, 1
				  FROM AllocationsUpdated AU
				 INNER JOIN AllocationsScheduledPersons ASP ON AU_AllocationsDutyID = ASP.ASP_AllocationsDutyID
				 INNER JOIN AllocationsAddPersons AAP ON AAP.AAP_AllocationsSPID = AU.AU_AllocationsSPID
				 INNER JOIN Allocations AL ON AL.AL_AllocationsID = AAP_AllocationsID
				 WHERE AL_AllocationsID = @vAllocationID
				   AND AU_STATUS = 0
				   AND NOT EXISTS ( SELECT 1
									  FROM @TempDutyList TL
									 WHERE TL.AllocationsDutyID = AU_AllocationsDutyID )

                DELETE AJP
                  FROM Allocations_Jobs_Publish AJP
                 INNER JOIN Allocations_Publish AP ON AP.ID=AJP.allocationid
				 INNER JOIN @TempDutyList TL ON TL.AllocationsSPID = AP.AllocationsSPID
				 WHERE TL.IsHomeTeam = 1

                DELETE AJP
                  FROM Allocations_Jobs_Publish AJP
                 INNER JOIN Allocations_Publish AP ON AP.ID=AJP.allocationid
				 INNER JOIN @TempDutyList TL ON TL.AllocationsDutyID = AP.AllocationsDutyID
				 WHERE TL.IsHomeTeam = 1

                DELETE AP
                  FROM Allocations_Publish AP
				 INNER JOIN @TempDutyList TL ON TL.AllocationsSPID = AP.AllocationsSPID
				 WHERE TL.IsHomeTeam = 1

                DELETE AP
                  FROM Allocations_Publish AP
				 INNER JOIN @TempDutyList TL ON TL.AllocationsDutyID = AP.AllocationsDutyID
				 WHERE TL.IsHomeTeam = 1

                DELETE AJP
                  FROM Allocations_Jobs_Publish AJP
                 INNER JOIN Allocations_Publish AP ON AP.ID=AJP.allocationid
				 INNER JOIN @TempDutyList TL ON TL.AllocationsSPID = AP.AllocationsSPID
				 WHERE TL.IsHomeTeam IN (0,2)
                   AND AP.AllocationID = @vAllocationID

                DELETE AJP
                  FROM Allocations_Jobs_Publish AJP
                 INNER JOIN Allocations_Publish AP ON AP.ID=AJP.allocationid
				 INNER JOIN @TempDutyList TL ON TL.AllocationsDutyID = AP.AllocationsDutyID
				 WHERE TL.IsHomeTeam IN (0,2)
                   AND AP.AllocationID = @vAllocationID

                DELETE AP
                  FROM Allocations_Publish AP
				 INNER JOIN @TempDutyList TL ON TL.AllocationsSPID = AP.AllocationsSPID
				 WHERE TL.IsHomeTeam IN (0,2)
                   AND AP.AllocationID = @vAllocationID

                DELETE AP
                  FROM Allocations_Publish AP
				 INNER JOIN @TempDutyList TL ON TL.AllocationsDutyID = AP.AllocationsDutyID
				 WHERE TL.IsHomeTeam IN (0,2)
                   AND AP.AllocationID = @vAllocationID

            INSERT INTO Allocations_Publish
                        (allocationid,
                         dutyname,
                         duration,
                         weeknumber,
                         iday,
                         starttime,
                         endtime,
                         sortcode,
                         leaveid,
                         dutycomments,
                         backcolour,
                         fontcolour,
                         personcomments,
                         adhocduty,
                         markedovertime,
                         markedptextraday,
                         markedcompleave,
                         markedsickness,
                         manualotamount,
                         manualotexcbreaksamount,
                         unallocated,
                         schedulingteamid,
                         schedulingpersonid,
                         dutydate,
                         startdate,
                         enddate,
                         isattention,
                         isrequest,
                         aftermidnight,
                         dutyprogramid,
						 DutyProgramId2,
						 DutyProgramId3,
						 DutyProgramId4,
						 DutyProgramId5,
						 DutyProgramId6,
                         dutybreaktime,
                         dutycolorid,
                         ispublished,
                         ishometeam,
                         markwiad,
                         markactual,
                         isedited,
                         mannualothours,
                         isactive,
                         iseditable,
                         origallocationid,
                         masterdutyid,
                         isactiveduty,
						 DutyTeamID,
						 LeaveStartTime,
						 LeaveEndTime,
						 AllocationsDutyID,
						 AllocationsSPID,
						 SigninStatus,
						 SigninStartTime,
						 SigninEndTime,
						 SigninINBuilding)
            SELECT al.AL_AllocationsID,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 'U' 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN CASE WHEN ASP_LeaveType = 1
										   THEN 'Leave'
										   WHEN ASP_LeaveType = 2
										   THEN 'OFF Leave'
										   WHEN ASP_LeaveType = 3
										   THEN 'Sick'
										   WHEN ASP_LeaveType = 4
										   THEN 'U-Sick'
										   WHEN ASP_LeaveType = 5
										   THEN '-Sick'
										   WHEN ASP_LeaveType = 7
										   THEN 'Absent'
									   END
							ELSE AD_DutyName END			
						END AS dutyname,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE 
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveDuration
										 ELSE AD_Duration 
									END 
				   END AS duration,
                   AL.AL_WeekNumber,
                   ASP_iDay,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_StartTimeSec END AS starttime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_EndTimeSec END AS endtime,
                   ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   ASP_Comments as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   ASP_MarkedOverTime markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   case when ASP_LeaveType in (3,4,5) then 1 else 0 end as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   al.AL_SchedulingTeamID,
                   ASP_SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyBreakTime END AS dutybreaktime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN ASP_LeaveColourID
								 ELSE AD.AD_DutyColourID END END AS dutycolorid,
                   1 as ispublished,
                   spl.ishometeam,
                   case when ASP_WIADStatus = 1 then 1 end as markwiad,
                   case when ASP_WIADStatus = 2 then 1 end as markactual,
                   0 isedited,
                   ASP_OverTimeHours mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   ASP_DutyTeamID,
				   ASP_LeaveStartTimeSec,
				   ASP_LeaveEndTimeSec,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   ASP_SigninStatus,
				   ASP_SigninStartTime,
				   ASP_SigninEndTime,
				   ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
			INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
													and spl.ScheduledPersonID = ASP_SchedulingPersonID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN @TempDutyList TL ON ASP.ASP_AllocationsSPID = TL.AllocationsSPID
			INNER JOIN Allocations AP on AP.AL_AllocationsID = AD.AD_AllocationsID
			WHERE SPL.scheduledType = 1
			  AND AD_DutyDate between SpL.startdate AND SpL.enddate
			  AND AL.AL_AllocationsID = @vAllocationID
			  AND TL.IsHomeTeam = 1
			  UNION ALL
            SELECT al.AL_AllocationsID,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 'U' 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN CASE WHEN ASP_LeaveType = 1
										   THEN 'Leave'
										   WHEN ASP_LeaveType = 2
										   THEN 'OFF Leave'
										   WHEN ASP_LeaveType = 3
										   THEN 'Sick'
										   WHEN ASP_LeaveType = 4
										   THEN 'U-Sick'
										   WHEN ASP_LeaveType = 5
										   THEN '-Sick'
										   WHEN ASP_LeaveType = 7
										   THEN 'Absent'
									   END
							ELSE AD_DutyName END			
						END AS dutyname,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE 
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveDuration
										 ELSE AD_Duration 
									END 
				   END AS duration,
                   AL.AL_WeekNumber,
                   ASP_iDay,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_StartTimeSec END AS starttime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_EndTimeSec END AS endtime,
                   ADP.AAP_SortCode ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   ADP.AAP_Comments as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   ASP_MarkedOverTime markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   case when ASP_LeaveType in (3,4,5) then 1 else 0 end as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   al.AL_SchedulingTeamID,
                   ASP_SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyBreakTime END AS dutybreaktime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN ASP_LeaveColourID
								 ELSE AD.AD_DutyColourID END END AS dutycolorid,
                   1 as ispublished,
                   spl.ishometeam,
                   case when ASP_WIADStatus = 1 then 1 end as markwiad,
                   case when ASP_WIADStatus = 2 then 1 end as markactual,
                   0 isedited,
                   ASP_OverTimeHours mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   ASP_DutyTeamID,
				   ASP_LeaveStartTimeSec,
				   ASP_LeaveEndTimeSec,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   ASP_SigninStatus,
				   ASP_SigninStartTime,
				   ASP_SigninEndTime,
				   ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN AllocationsAddPersons ADP on AL_AllocationsID = ADP.AAP_AllocationsID
			INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
			INNER JOIN AllocationsScheduledPersons ASP on ADP.AAP_AllocationsSPID = ASP_AllocationsSPID
													and spl.ScheduledPersonID = ASP_SchedulingPersonID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN @TempDutyList TL ON TL.AllocationsSPID = ASP.ASP_AllocationsSPID
			INNER JOIN Allocations AP on AP.AL_AllocationsID = AD.AD_AllocationsID
			WHERE SPL.scheduledType = 1
			  AND AD_DutyDate between SpL.startdate AND SpL.enddate
			  AND AL.AL_AllocationsID = @vAllocationID
			  AND TL.IsHomeTeam IN (0,2)
			  UNION ALL
            SELECT al.AL_AllocationsID,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 'U' 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN CASE WHEN ASP_LeaveType = 1
										   THEN 'Leave'
										   WHEN ASP_LeaveType = 2
										   THEN 'OFF Leave'
										   WHEN ASP_LeaveType = 3
										   THEN 'Sick'
										   WHEN ASP_LeaveType = 4
										   THEN 'U-Sick'
										   WHEN ASP_LeaveType = 5
										   THEN '-Sick'
										   WHEN ASP_LeaveType = 7
										   THEN 'Absent'
									   END
							ELSE AD_DutyName END			
						END AS dutyname,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE 
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveDuration
										 ELSE AD_Duration 
									END 
				   END AS duration,
                   AL.AL_WeekNumber,
                   ASP_iDay,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_StartTimeSec END AS starttime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_EndTimeSec END AS endtime,
                   ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   ASP_Comments as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   ASP_MarkedOverTime markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   case when ASP_LeaveType in (3,4,5) then 1 else 0 end as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   al.AL_SchedulingTeamID,
                   ASP_SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyBreakTime END AS dutybreaktime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN ASP_LeaveColourID
								 ELSE AD.AD_DutyColourID END END AS dutycolorid,
                   1 as ispublished,
                   spl.ishometeam,
                   case when ASP_WIADStatus = 1 then 1 end as markwiad,
                   case when ASP_WIADStatus = 2 then 1 end as markactual,
                   0 isedited,
                   ASP_OverTimeHours mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   ASP_DutyTeamID,
				   ASP_LeaveStartTimeSec,
				   ASP_LeaveEndTimeSec,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   ASP_SigninStatus,
				   ASP_SigninStartTime,
				   ASP_SigninEndTime,
				   ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
			INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
													and spl.ScheduledPersonID = ASP_SchedulingPersonID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN @TempDutyList TL ON TL.AllocationsDutyID = AD_AllocationsDutyID
			INNER JOIN Allocations AP on AP.AL_AllocationsID = AD.AD_AllocationsID
			WHERE SPL.scheduledType = 1
			  AND AD_DutyDate between SpL.startdate AND SpL.enddate
			  AND AL.AL_AllocationsID = @vAllocationID
			  AND TL.IsHomeTeam = 1
			  AND ISNULL(TL.AllocationsSPID,0) = 0
			  UNION ALL
            SELECT al.AL_AllocationsID,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 'U' 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN CASE WHEN ASP_LeaveType = 1
										   THEN 'Leave'
										   WHEN ASP_LeaveType = 2
										   THEN 'OFF Leave'
										   WHEN ASP_LeaveType = 3
										   THEN 'Sick'
										   WHEN ASP_LeaveType = 4
										   THEN 'U-Sick'
										   WHEN ASP_LeaveType = 5
										   THEN '-Sick'
										   WHEN ASP_LeaveType = 7
										   THEN 'Absent'
									   END
							ELSE AD_DutyName END			
						END AS dutyname,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE 
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveDuration
										 ELSE AD_Duration 
									END 
				   END AS duration,
                   AL.AL_WeekNumber,
                   ASP_iDay,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_StartTimeSec END AS starttime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_EndTimeSec END AS endtime,
                   ADP.AAP_SortCode ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   ADP.AAP_Comments as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   ASP_MarkedOverTime markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   case when ASP_LeaveType in (3,4,5) then 1 else 0 end as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   al.AL_SchedulingTeamID,
                   ASP_SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyBreakTime END AS dutybreaktime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN ASP_LeaveColourID
								 ELSE AD.AD_DutyColourID END END AS dutycolorid,
                   1 as ispublished,
                   spl.ishometeam,
                   case when ASP_WIADStatus = 1 then 1 end as markwiad,
                   case when ASP_WIADStatus = 2 then 1 end as markactual,
                   0 isedited,
                   ASP_OverTimeHours mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   ASP_DutyTeamID,
				   ASP_LeaveStartTimeSec,
				   ASP_LeaveEndTimeSec,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   ASP_SigninStatus,
				   ASP_SigninStartTime,
				   ASP_SigninEndTime,
				   ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN AllocationsAddPersons ADP on AL_AllocationsID = ADP.AAP_AllocationsID
			INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
			INNER JOIN AllocationsScheduledPersons ASP on ADP.AAP_AllocationsSPID = ASP_AllocationsSPID
													and spl.ScheduledPersonID = ASP_SchedulingPersonID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN @TempDutyList TL ON TL.AllocationsDutyID = AD_AllocationsDutyID
			INNER JOIN Allocations AP on AP.AL_AllocationsID = AD.AD_AllocationsID
			WHERE SPL.scheduledType = 1
			  AND AD_DutyDate between SpL.startdate AND SpL.enddate
			  AND AL.AL_AllocationsID = @vAllocationID
			  AND TL.IsHomeTeam IN (0,2)
			  AND ISNULL(TL.AllocationsSPID,0) = 0
			  UNION ALL
		   SELECT al.AL_AllocationsID,
                   AD_DutyName,
                   AD_Duration AS duration,
                   AL.AL_WeekNumber,
                   AD_iDay,
                   AD_StartTimeSec  AS starttime,
                   AD_EndTimeSec  AS endtime,
                   NULL AS ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   NULL as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   0 markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   0 as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   al.AL_SchedulingTeamID,
                   0 As SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   AD_DutyBreakTime  AS dutybreaktime,
                   AD_DutyColourID  AS dutycolorid,
                   1 as ispublished,
                   1 as ishometeam,
                   0 as markwiad,
                   0 as markactual,
                   0 as isedited,
                   0 as  mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   0 as DutyTeamID,
				   0 as LeaveStartTimeSec,
				   0 as LeaveEndTimeSec,
				   AD_AllocationsDutyID as AllocationsDutyID,
				   0 as AllocationsSPID,
				   0 as ASP_SigninStatus,
				   0 as ASP_SigninStartTime,
				   0 as ASP_SigninEndTime,
				   0 as ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID
			INNER JOIN @TempDutyList TL ON TL.AllocationsDutyID = AD_AllocationsDutyID
			WHERE AL.AL_AllocationsID = @vAllocationID
			  AND AD_DutyType < 7
			  AND AD_DutyStatus = 0
			  AND TL.IsHomeTeam = 1
			  UNION ALL
            SELECT ap.AL_AllocationsID,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 'U' 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN CASE WHEN ASP_LeaveType = 1
										   THEN 'Leave'
										   WHEN ASP_LeaveType = 2
										   THEN 'OFF Leave'
										   WHEN ASP_LeaveType = 3
										   THEN 'Sick'
										   WHEN ASP_LeaveType = 4
										   THEN 'U-Sick'
										   WHEN ASP_LeaveType = 5
										   THEN '-Sick'
										   WHEN ASP_LeaveType = 7
										   THEN 'Absent'
									   END
							ELSE AD_DutyName END			
						END AS dutyname,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE 
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveDuration
										 ELSE AD_Duration 
									END 
				   END AS duration,
                   AL.AL_WeekNumber,
                   ASP_iDay,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_StartTimeSec END AS starttime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_EndTimeSec END AS endtime,
                   ADP.AAP_SortCode ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   ADP.AAP_Comments as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   ASP_MarkedOverTime markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   case when ASP_LeaveType in (3,4,5) then 1 else 0 end as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   ap.AL_SchedulingTeamID,
                   ASP_SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyBreakTime END AS dutybreaktime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN ASP_LeaveColourID
								 ELSE AD.AD_DutyColourID END END AS dutycolorid,
                   1 as ispublished,
                   spl.ishometeam,
                   case when ASP_WIADStatus = 1 then 1 end as markwiad,
                   case when ASP_WIADStatus = 2 then 1 end as markactual,
                   0 isedited,
                   ASP_OverTimeHours mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   ASP_DutyTeamID,
				   ASP_LeaveStartTimeSec,
				   ASP_LeaveEndTimeSec,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   ASP_SigninStatus,
				   ASP_SigninStartTime,
				   ASP_SigninEndTime,
				   ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
			INNER JOIN AllocationsAddPersons ADP on ADP.AAP_AllocationsSPID = ASP_AllocationsSPID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN @TempDutyList TL ON TL.AllocationsSPID = ASP_AllocationsSPID
			INNER JOIN Allocations AP on AP.AL_AllocationsID = AAP_AllocationsID
			INNER JOIN ScheduledPersonTeam_LINK SPL on SPL.TeamID = AP.AL_SchedulingTeamID
													and SPL.ScheduledPersonID = ASP_SchedulingPersonID
			WHERE SPL.scheduledType = 1
			  AND SPL.IsHomeTeam IN (0,2)
			  AND AD_DutyDate between SpL.startdate AND SpL.enddate
			  AND TL.IsHomeTeam = 1
			  AND AP.AL_Status IN ( 1,2)
			  AND AL.AL_AllocationsID = @vAllocationID
			  UNION ALL
            SELECT ap.AL_AllocationsID,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 'U' 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN CASE WHEN ASP_LeaveType = 1
										   THEN 'Leave'
										   WHEN ASP_LeaveType = 2
										   THEN 'OFF Leave'
										   WHEN ASP_LeaveType = 3
										   THEN 'Sick'
										   WHEN ASP_LeaveType = 4
										   THEN 'U-Sick'
										   WHEN ASP_LeaveType = 5
										   THEN '-Sick'
										   WHEN ASP_LeaveType = 7
										   THEN 'Absent'
									   END
							ELSE AD_DutyName END			
						END AS dutyname,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE 
									CASE WHEN AD_DutyType IN (8,11,12)
										 THEN ASP_LeaveDuration
										 ELSE AD_Duration 
									END 
				   END AS duration,
                   AL.AL_WeekNumber,
                   ASP_iDay,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_StartTimeSec END AS starttime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_EndTimeSec END AS endtime,
                   ADP.AAP_SortCode ASP_SortCode,
                   0 as leaveid,
                   AD_Comments as DutyComments,
                   null as backcolour,
                   null as fontcolour,
                   ADP.AAP_Comments as personcomments,
                   case when AD_DutyType = 6 then 1 else 0 end as  adhocduty,
                   ASP_MarkedOverTime markedovertime,
                   0 markedptextraday,
                   0 markedcompleave,
                   case when ASP_LeaveType in (3,4,5) then 1 else 0 end as  markedsickness,
                   0 manualotamount,
                   0 as manualotexcbreaksamount,
                   0 as unallocated,
                   ap.AL_SchedulingTeamID,
                   ASP_SchedulingPersonID,
                   AD_DutyDate as  dutydate,
                   AD_DutyStartTimeLocal startdate,
                   AD_DutyEndTimeLocal enddate,
                   AD_isAttention,
                   AD_isRequest,
                   case when DATEDIFF(DAY,AD_DutyStartTimeLocal,AD_DutyEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   AD_DutyProgramID1,
				   AD_DutyProgramID2,
				   AD_DutyProgramID3,
				   AD_DutyProgramID4,
				   AD_DutyProgramID5,
				   AD_DutyProgramID6,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 ELSE AD_DutyBreakTime END AS dutybreaktime,
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID
				         AND ISNULL(AP.AL_Status,0) = 0
				        THEN 0 
						ELSE CASE WHEN AD_DutyType IN (8,11,12)
								 THEN ASP_LeaveColourID
								 ELSE AD.AD_DutyColourID END END AS dutycolorid,
                   1 as ispublished,
                   spl.ishometeam,
                   case when ASP_WIADStatus = 1 then 1 end as markwiad,
                   case when ASP_WIADStatus = 2 then 1 end as markactual,
                   0 isedited,
                   ASP_OverTimeHours mannualothours,
                   CASE WHEN AD_DutyStatus IN (0,1) THEN 1 ELSE 0 END isactive,
                   1 iseditable,
                   0 origallocationid,
                   AD_MasterDutyID masterdutyid,
                   1 isactiveduty,
				   ASP_DutyTeamID,
				   ASP_LeaveStartTimeSec,
				   ASP_LeaveEndTimeSec,
				   AD_AllocationsDutyID,
				   ASP_AllocationsSPID,
				   ASP_SigninStatus,
				   ASP_SigninStartTime,
				   ASP_SigninEndTime,
				   ASP_SigninINBuilding
            FROM   Allocations AS AL (nolock)
			INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID
			INNER JOIN AllocationsAddPersons ADP on ADP.AAP_AllocationsSPID = ASP_AllocationsSPID
			INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID
			INNER JOIN @TempDutyList TL ON TL.AllocationsDutyID = AD_AllocationsDutyID
			INNER JOIN Allocations AP on AP.AL_AllocationsID = AAP_AllocationsID
			INNER JOIN ScheduledPersonTeam_LINK SPL on SPL.TeamID = AP.AL_SchedulingTeamID
													and SPL.ScheduledPersonID = ASP_SchedulingPersonID
			WHERE SPL.scheduledType = 1
			  AND SPL.IsHomeTeam IN (0,2)
			  AND AD_DutyDate between SpL.startdate AND SpL.enddate
			  AND TL.IsHomeTeam = 1
			  AND AP.AL_Status IN ( 1,2)
			  AND AL.AL_AllocationsID = @vAllocationID

        INSERT INTO [dbo].[Allocations_Jobs_Publish]
                    (allocateinstanceid,
                     departmentid,
                     allocationid,
                     allocatejobid,
                     staffnumber,
                     weeknumber,
                     iday,
                     programme,
                     contact,
                     location,
                     jobname,
                     starttime,
                     endtime,
                     jobbackcolour,
                     jobfontcolour,
                     comments,
                     masterjobid,
                     adhocduty,
                     unallocated,
                     schedulingpersonid,
                     programmeid,
                     jobdefaultcolour,
                     aftermidnight,
                     schedulingteamid,
                     edited,
                     ispublished,
                     isedited,
                     isactive,
                     job_info)
            SELECT 0 allocateinstanceid,
                   0 departmentid,
                   AP.ID,
                   AJ.AJ_AllocateJobID,
                   null as staffnumber,
                   0 as weeknumber,
                   0 as iday,
                   pg.programme,
                   AJ.AJ_Contact as contact,
                   AJ.AJ_Location,
                   AJ_JobName,
                   AJ_JobStartTimeSec,
                   AJ_JobEndTimeSec,
                   AJ_JobBGColour jobbackcolour,
                   AJ_JobFontColour jobfontcolour,
                   AJ_Comments comments,
                   AJ_MasterJobID as masterjobid,
                   0 adhocduty,
                   case when AJ_JobStatus = 0 then 1 else 0 end as unallocated,
                   0 schedulingpersonid,
                   AJ_ProgrammeID,
                   NULL as jobdefaultcolour,
                   case when DATEDIFF(DAY,AJ_JobStartTimeLocal,AJ_JobEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   0 as schedulingteamid,
                   0 as edited,
                   1 as ispublished,
                   0 as isedited,
                   case when AJ_JobStatus in (0,1) then 1
					    when AJ_JobStatus = 9 then 0
						else 1 end as isactive,
                   AJ_JobInfo
             FROM AllocationsJobs AJ
            INNER JOIN AllocationsDuties AD on AD.AD_AllocationsDutyID = AJ.AJ_AllocationsDutyID
            INNER JOIN Allocations_Publish AP ON AD.AD_AllocationsID = AP.allocationid AND AD.AD_AllocationsDutyID = AP.AllocationsDutyID
			INNER JOIN @TempDutyList TL ON TL.AllocationsDutyID = AD.AD_AllocationsDutyID
			LEFT JOIN Programmes pg on pg.ID = aj.AJ_ProgrammeID
            WHERE AD_AllocationsID = @vAllocationID

		   UPDATE AU
		      SET AU.AU_STATUS = 1
             FROM AllocationsUpdated AU
			INNER JOIN @TempDutyList TL ON  TL.AllocationsSPID = AU.AU_AllocationsSPID
			WHERE AU.AU_STATUS = 0
			  AND TL.IsHomeTeam = 1

		   UPDATE AU
		      SET AU.AU_STATUS = 1
             FROM AllocationsUpdated AU
			INNER JOIN @TempDutyList TL ON  TL.AllocationsDutyID = AU.AU_AllocationsDutyID
			WHERE AU.AU_STATUS = 0
			  AND TL.IsHomeTeam = 1

                UPDATE Allocations
                   SET AL_UpdatedBy = @vuserID ,
                       AL_UpdatedDate = GETDATE()
                WHERE AL_AllocationsID = @vAllocationID

				SET @History = 'Week '+RIGHT(cast(@weekNumber as VARCHAR),2) + '/'
				               + LEFT(cast(@weekNumber as VARCHAR),len(cast(@weekNumber as VARCHAR))-2)
							   +' was published on '
							   + FORMAT(Getdate(),'dd/MM/yyyy') + ' at '
							   + FORMAT(Getdate(),'HH:mm')
							   + ' by '
							   + @vname

				EXEC [usp_mod_AllocationHistory] @vAllocationid,@HistoryTypeID,@vuserID,@History,1;

		  END

        IF ( @@TRANCOUNT  > 0 )
         BEGIN
           COMMIT  TRANSACTION
         END

		 SELECT 0 as SPExecStatus,
			    'Success' as SPMessage

        END TRY

        BEGIN CATCH

        IF ( @@TRANCOUNT  > 0 )
         BEGIN
             ROLLBACK  TRANSACTION
         END

		 INSERT INTO ErrorLog
		        (ErrorNumber,
				 ErrorState,
				 ErrorSeverity,
				 ErrorProcedure,
				 ErrorLine,
				 ErrorMessage,
				 ErrorDateTime,
				 UserName
				)
         SELECT ERROR_NUMBER() AS ErrorNumber,
                ERROR_STATE() AS ErrorState,
				ERROR_SEVERITY() AS ErrorSeverity,
				ERROR_PROCEDURE() AS ErrorProcedure,
				ERROR_LINE() AS ErrorLine,
				ERROR_MESSAGE() AS ErrorMessage,
				getutcdate(),
				@vuserID

		 SELECT @@IDENTITY as SPExecStatus,
			    ERROR_MESSAGE() as SPMessage

        END CATCH;

END