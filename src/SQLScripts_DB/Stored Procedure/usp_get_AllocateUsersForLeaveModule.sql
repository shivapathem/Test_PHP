USE [BBCSchedules_WP]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_AllocateUsersForLeaveModule]    Script Date: 30/03/2026 14:21:09 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER        PROCEDURE [dbo].[usp_get_AllocateUsersForLeaveModule]
  -- Add the parameters for the stored procedure here
  @groupid int
AS
  BEGIN
      -- SET NOCOUNT ON added to prevent extra result sets from
      -- interfering with SELECT statements.
      SET NOCOUNT ON;

      -- Select statements for procedure here
      select *
      from   (select *,
                     row_number()
                       over (
                         partition by ScheduledPersonID
                         order by rowNo desc) as rowNo1
              from   (SELECT UD_NetLogin NetLogin,
                             UD_DisplayLastName+', '+UD_DisplayFirstName AS userDisplayName,
                             CASE
                               WHEN st.schedulingTeamId IS NULL THEN '-'
                               ELSE st.schedulingTeamName
                             END AS teamname,
                             UD_UserID ScheduledPersonID,
                             2   as rowNo
                      from   UserDetails u
                             INNER join ScheduledPersonTeam_LINK spl (NOLOCK)
                                     on spl.ScheduledPersonID = UD_UserID                                        
                             INNER join schedulingTeams st (NOLOCK)
                                     on st.schedulingTeamId = spl.TeamID
                                        and st.isActive = 1
                      WHERE  NOT EXISTS (SELECT 1
                                         FROM  Staff_Web_Config_LeaveGroups_Link sdlc
                                         WHERE  UD_UserID = sdlc.ScheduledPersonID
                                                and ( LeaveGroupID = @groupid )
                                                and isActive = 1)
                        and spl.IsHomeTeam = 1
                        AND CAST(GETDATE() AS DATE) between StartDate and EndDate
                      UNION
                      select *
                      from   (SELECT UD_NetLogin NetLogin,
                                     UD_DisplayLastName+', '+UD_DisplayFirstName AS
                                     userDisplayName,
                                     CASE
                                       WHEN st.schedulingTeamId IS NULL THEN '-'
                                       ELSE st.schedulingTeamName
                                     END                              AS
                                     teamname,
                                     UD_UserID ScheduledPersonID,
                                     row_number()
                                       over (
                                         partition by spl.ScheduledPersonID
                                         order by spl.isDefault desc) as rowNo
                              from   UserDetails u
                                     INNER join ScheduledPersonTeam_LINK spl (
                                                NOLOCK)
                                             on spl.ScheduledPersonID =
                                                UD_UserID                                                
                                     INNER join schedulingTeams st (NOLOCK)
                                             on st.schedulingTeamId = spl.TeamID
                                                and st.isActive = 1
                              WHERE  NOT EXISTS (SELECT 1
                                                 FROM
                                         Staff_Web_Config_LeaveGroups_Link
                                         sdlc
                                                 WHERE  UD_UserID = sdlc.ScheduledPersonID
                                                        and ( LeaveGroupID =
                                                              @groupid
                                                            )
                                                        and isActive = 1)
                                and spl.IsHomeTeam IN (0,2)
                                                and spl.scheduledType = 0
                                                AND CAST(GETDATE() AS DATE) between StartDate and EndDate
                                                        ) as
                             qry
                      where  qry.rowNo = 1) as qry2) as qry3
      where  qry3.rowNo1 = 1
      ORDER  BY userDisplayName
  END