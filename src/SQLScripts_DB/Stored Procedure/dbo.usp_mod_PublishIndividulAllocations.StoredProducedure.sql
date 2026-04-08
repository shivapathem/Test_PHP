USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_PublishIndividulAllocations]    Script Date: 30/03/2026 15:25:00 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER            PROCEDURE [dbo].[usp_mod_PublishIndividulAllocations] 
@AllocationsID       INT,
@AllocationsDutyID   INT,
@AllocationsSPID     INT
AS
BEGIN

    SET NOCOUNT ON;

    DECLARE @vSQL       VARCHAR(MAX),
            @vSQL1      VARCHAR(MAX);
        
    BEGIN TRY
        BEGIN TRANSACTION  

        IF EXISTS (SELECT TOP 1 AL_WeekNumber 
                     FROM Allocations
                    WHERE AL_AllocationsID = @AllocationsID
                      AND AL_Status = 1)        
          BEGIN

            SET @vSQL = 'DELETE AJP 
                  FROM Allocations_Jobs_Publish AJP 
                 INNER JOIN Allocations_Publish AP ON AP.ID=AJP.allocationid
                 WHERE 1 = 1 '
                   + CASE WHEN ISNULL(@AllocationsDutyID,0) > 0
                          THEN 'AND AP.AllocationsDutyID =  '+CAST(@AllocationsDutyID AS VARCHAR)
                          WHEN ISNULL(@AllocationsSPID,0) > 0 
                          THEN 'AND AP.AllocationsSPID = '+CAST(@AllocationsSPID AS VARCHAR)
                      END
            EXEC ( @vSQL )

            SET @vSQL = 'DELETE AP 
                  FROM Allocations_Publish AP 
                 WHERE 1 = 1 '
                   + CASE WHEN ISNULL(@AllocationsDutyID,0) > 0
                          THEN 'AND AP.AllocationsDutyID =  '+CAST(@AllocationsDutyID AS VARCHAR)
                          WHEN ISNULL(@AllocationsSPID,0) > 0 
                          THEN 'AND AP.AllocationsSPID = '+CAST(@AllocationsSPID AS VARCHAR)
                      END
            EXEC ( @vSQL )                 

            SET @vSQL = 'INSERT INTO Allocations_Publish
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
                         AllocationsSPID)
            SELECT al.AL_AllocationsID,                   
                   CASE WHEN ISNULL(ASP_DutyTeamID,0) > 0 AND ASP_DutyTeamID <> AL.AL_SchedulingTeamID 
                         AND ISNULL(AP.AL_Status,0) = 0
                        THEN ''U''                      
                        ELSE CASE WHEN AD_DutyType IN (8,11,12)
                                 THEN CASE WHEN ASP_LeaveType = 1
                                           THEN ''Leave''
                                           WHEN ASP_LeaveType = 2
                                           THEN ''OFF Leave''
                                           WHEN ASP_LeaveType = 3
                                           THEN ''Sick''
                                           WHEN ASP_LeaveType = 4
                                           THEN ''U-Sick''
                                           WHEN ASP_LeaveType = 5
                                           THEN ''-Sick''
                                           WHEN ASP_LeaveType = 7
                                           THEN ''Absent''
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
                   ASP_OverTimeHours manualotamount,
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
                   ASP_AllocationsSPID
            FROM   Allocations AS AL (nolock) '



            SET @vSQL1 = @vSQL + 'INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
                INNER JOIN AllocationsScheduledPersons ASP on AL_AllocationsID = ASP_AllocationsID 
                                                        and spl.ScheduledPersonID = ASP_SchedulingPersonID
                INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID 
                INNER JOIN Allocations AP on AP.AL_AllocationsID = AD.AD_AllocationsID
                WHERE SPL.scheduledType = 1 
                  AND SPL.isHometeam = 1
                  AND AL.AL_Status IN (0,1)
                  AND AD_DutyDate between SpL.startdate AND SpL.enddate '
                  + CASE WHEN ISNULL(@AllocationsSPID, 0 ) > 0
                         THEN ' AND ASP.ASP_AllocationsSPID = '+CAST(@AllocationsSPID  AS varchar)
                         WHEN ISNULL(@AllocationsSPID, 0 ) = 0 AND ISNULL(@AllocationsDutyID,0) > 0
                         THEN ' AND AD.AD_AllocationsID = '+CAST(@AllocationsDutyID  AS varchar)
                     END

            EXEC ( @vSQL1)

            SET @vSQL = REPLACE(@vSQL,'ASP_SortCode','AAP_SortCode')
            SET @vSQL = REPLACE(@vSQL,'ASP_Comments','AAP_Comments')

            SET @vSQL1 = @vSQL + 'INNER JOIN ScheduledPersonTeam_LINK (nolock) AS spl on spl.TeamID = AL_SchedulingTeamID
                INNER JOIN AllocationsAddPersons AA on AL.AL_AllocationsID = AAP_AllocationsID 
                INNER JOIN AllocationsScheduledPersons ASP on AAP_AllocationsSPID = ASP_AllocationsSPID
                INNER JOIN AllocationsDuties AD on AD_AllocationsDutyID = ASP_AllocationsDutyID 
                INNER JOIN Allocations AP on AP.AL_AllocationsID = AD.AD_AllocationsID
                WHERE SPL.scheduledType = 1 
                  AND SPL.isHometeam IN (0,2)
                  AND AL.AL_Status IN (0,1)
                  AND AD_DutyDate between SpL.startdate AND SpL.enddate '
                  + CASE WHEN ISNULL(@AllocationsSPID, 0 ) > 0
                         THEN ' AND ASP.ASP_AllocationsSPID = '+CAST(@AllocationsSPID  AS varchar)
                         WHEN ISNULL(@AllocationsSPID, 0 ) = 0 AND ISNULL(@AllocationsDutyID,0) > 0
                         THEN ' AND AD.AD_AllocationsID = '+CAST(@AllocationsDutyID  AS varchar)
                     END
            
            EXEC ( @vSQL1)
            
        IF ( ISNULL(@AllocationsSPID, 0 ) = 0 AND ISNULL(@AllocationsDutyID,0) > 0)
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
                         AllocationsSPID)
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
                   0 as AllocationsDutyID,
                   0 as AllocationsSPID
            FROM   Allocations AS AL (nolock)
            INNER JOIN AllocationsDuties AD on AD_AllocationsID = AL_AllocationsID  
            WHERE  AD_AllocationsDutyID =  @AllocationsDutyID
              AND ISNULL(@AllocationsSPID,0) = 0
              AND AD_DutyType < 7
              AND AD_DutyStatus = 0
              AND AL_Status in (0,1)
        
        END

        SET @vSQL = 'INSERT INTO [dbo].[Allocations_Jobs_Publish]
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
                   1 unallocated,
                   0 schedulingpersonid,
                   AJ_ProgrammeID,
                   NULL as jobdefaultcolour,
                   case when DATEDIFF(DAY,AJ_JobStartTimeLocal,AJ_JobEndTimeLocal) = 1 then 1 else 0 end as aftermidnight,
                   0 as schedulingteamid,
                   0 as edited,
                   1 as ispublished,
                   0 as isedited,
                   AJ_JobStatus as isactive,
                   AJ_JobInfo
             FROM AllocationsJobs AJ
            INNER JOIN AllocationsDuties AD on AD.AD_AllocationsDutyID = AJ.AJ_AllocationsDutyID
            INNER JOIN Allocations AL ON AL_AllocationsID = AD_AllocationsID '
                  + CASE WHEN ISNULL(@AllocationsDutyID, 0 ) > 0
                         THEN ' INNER JOIN Allocations_Publish AP ON AD.AD_AllocationsID = AP.allocationid 
                                    AND AD.AD_AllocationsDutyID = AD.AD_AllocationsDutyID
            LEFT join Programmes pg on pg.ID = aj.AJ_ProgrammeID
            WHERE AD.AD_AllocationsDutyID =  '+CAST(@AllocationsDutyID  AS varchar)
                         WHEN ISNULL(@AllocationsSPID, 0 ) > 0 AND ISNULL(@AllocationsDutyID,0) = 0
                         THEN ' INNER JOIN AllocationsScheduledPersons ASP on AD_AllocationsDutyID = ASP_AllocationsDutyID
                         INNER JOIN Allocations_Publish AP ON AD.AD_AllocationsID = AP.allocationid 
                                    AND AD.AD_AllocationsDutyID = AD.AD_AllocationsDutyID
            LEFT join Programmes pg on pg.ID = aj.AJ_ProgrammeID
            WHERE ASP.ASP_AllocationsSPID = '+CAST(@AllocationsSPID  AS varchar)
                     END
            
            EXEC ( @vSQL)


        END

        IF ( @@TRANCOUNT  > 0 ) 
         BEGIN
           COMMIT  TRANSACTION 
         END          
 
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
                1                       
          
        END CATCH;

END