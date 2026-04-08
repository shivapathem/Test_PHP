USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_LeaveBalanceByAreaReport]    Script Date: 20/11/2025 20:57:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_LeaveBalanceByAreaReport]
@LeaveYear         INT,
@NetLogin          NVARCHAR(25)
AS
BEGIN

	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.

	SET NOCOUNT ON;

	DECLARE @LeaveYearStartDate     date,
	        @LeaveYearEndDate       date;

	set @LeaveYearStartDate = DATEFROMPARTS(@LeaveYear,04,01)
	set @LeaveYearEndDate   = DATEFROMPARTS(@LeaveYear+1,03,31)

			SELECT  DisplayName,				           
					StaffNumber, 
					iyear LeaveYear,
					ScheduledPersonID,
					netlogin,
					ISNULL(Annual,0) - ISNULL(AnnualTaken,0) AS AnnualRemaining,
					ISNULL(PHL,0) - ISNULL(PHLTaken,0) AS PHLRemaining,
					ISNULL(comp,0) - ISNULL(CompTaken,0) AS compRemaining,
					ISNULL(Additional,0) - ISNULL(AdditionalTaken,0) AdditionalRemaining,
					ISNULL(Exceptional,0) - ISNULL(ExceptionalTaken,0) AS ExceptionalRemaining,
					ISNULL(Under11TOIL,0) - ISNULL(Under11TOILTaken,0) AS Under11TOILRemaining,
					ISNULL(Over12TOIL,0) - ISNULL(Over12TOILTaken,0) AS Over12TOILRemaining,
					ISNULL(Casual,0) - ISNULL(CasualTaken,0) AS CasualRemaining,
					ISNULL(LongService,0) - ISNULL(LongServiceTaken,0) AS LongServiceRemaining,
					ISNULL(Other,0) - ISNULL(OtherTaken,0) OtherRemaining,
					ISNULL(Annual,0)+ISNULL(comp,0)+ISNULL(PHL,0)+ISNULL(Additional,0)+
					ISNULL(Exceptional,0)+ ISNULL(Under11TOIL,0)+ ISNULL(Over12TOIL,0)+ ISNULL(Casual,0)+
					ISNULL(LongService,0)+ ISNULL(Other,0) AS TotalLeaveAllocated,
					ISNULL(AnnualTaken,0) + ISNULL( PHLTaken,0) + ISNULL( CompTaken,0) + ISNULL( AdditionalTaken,0) + 
					ISNULL(ExceptionalTaken,0) + ISNULL( Under11TOILTaken,0) + ISNULL(	Over12TOILTaken,0) + 
					ISNULL(CasualTaken,0) + ISNULL( LongServiceTaken,0) + ISNULL( OtherTaken,0) AS TotalLeaveTaken,
					(ISNULL(Annual,0)+ISNULL(comp,0)+ISNULL(PHL,0)+ISNULL(Additional,0)+
					ISNULL(Exceptional,0)+ ISNULL(Under11TOIL,0)+ ISNULL(Over12TOIL,0)+ ISNULL(Casual,0)+
					ISNULL(LongService,0)+ ISNULL(Other,0) ) -
					(ISNULL(AnnualTaken,0) + ISNULL( PHLTaken,0) + ISNULL( CompTaken,0) + ISNULL( AdditionalTaken,0) + 
					ISNULL(ExceptionalTaken,0) + ISNULL( Under11TOILTaken,0) + ISNULL(	Over12TOILTaken,0) + 
					ISNULL(CasualTaken,0) + ISNULL( LongServiceTaken,0) + ISNULL( OtherTaken,0) ) AS TotalLeaveRemaining
					into #Temp			    
				FROM
			(SELECT LA.Annual AS Annual,
					LA.Comp AS comp,
					LA.PHL AS PHL,
					LA.Additional AS Additional,
					LA.Exceptional AS Exceptional,
					LA.Under11TOIL AS Under11TOIL,
					LA.Over12TOIL AS Over12TOIL,
					LA.Casual AS Casual,
					LA.LongService AS LongService,
					LA.Other AS Other, 
					ISNULL(LAP.AnnualTaken,0) AS AnnualTaken,
					ISNULL(LAP.PHLTaken,0) AS PHLTaken,
					ISNULL(LAP.CompTaken,0) AS CompTaken,
					ISNULL(LAP.AdditionalTaken,0) AS AdditionalTaken,
					ISNULL(LAP.ExceptionalTaken,0) AS ExceptionalTaken,
					ISNULL(LAP.Under11TOILTaken,0) AS Under11TOILTaken,	
					ISNULL(LAP.Over12TOILTaken,0) AS Over12TOILTaken,
					ISNULL(LAP.CasualTaken,0) AS CasualTaken,
					ISNULL(LAP.LongServiceTaken,0) AS LongServiceTaken,
					ISNULL(LAP.OtherTaken,0) AS OtherTaken,
					isnull(LA.ScheduledPersonID,  LAP.ScheduledPersonID) as  ScheduledPersonID,
					isnull(LA.StaffNumber, lap.staffnumber) as staffnumber,
					isnull(LA.DisplayName, LAP.DisplayName) AS DisplayName,
					LA.iyear,
					LA.Netlogin
			FROM 
		   ( SELECT SUM(LA.Annual) AS Annual,
					SUM(LA.Comp) AS comp,
					SUM(LA.PHL) AS PHL,
					SUM(LA.Additional) AS Additional,
					SUM(LA.Exceptional) AS Exceptional,
					SUM(LA.Under11TOIL) AS Under11TOIL,
					SUM(LA.Over12TOIL) AS Over12TOIL,
					SUM(LA.Casual) AS Casual,
					SUM(LA.LongService) AS LongService,
					SUM(LA.Other) AS Other,
					Sp.UD_UserID as ScheduledPersonID,  
					sp.UD_StaffNumber as StaffNumber, 
					sp.UD_DisplayName AS DisplayName,
					iyear,
					sp.UD_NetLogin as netlogin
			   FROM Leaveallocation as LA
			  INNER JOIN UserDetails AS sp ON sp.UD_UserID = LA.SchedulingPersonID
			  WHERE LA.SchedulingPersonID > 0
				AND LA.IsActive = 1 
				AND LA.iYear = @LeaveYear
			  GROUP BY SP.UD_UserID,  
						sp.UD_StaffNumber, 
						sp.UD_DisplayName,
						iyear,
						sp.UD_NetLogin
			) LA
			full JOIN 
			(
				SELECT SUM(CASE WHEN LT.AllocName='Annual' THEN RA.Amount ELSE 0 END) AS AnnualTaken,
						SUM(CASE WHEN LT.AllocName='PHL' THEN RA.Amount ELSE 0 END) AS PHLTaken,
						SUM(CASE WHEN LT.AllocName='Comp' THEN RA.Amount ELSE 0 END) AS CompTaken,
						SUM(CASE WHEN LT.AllocName='Additional' THEN RA.Amount ELSE 0 END) AS AdditionalTaken,
						SUM(CASE WHEN LT.AllocName='Exceptional' THEN RA.Amount ELSE 0 END) AS ExceptionalTaken,
						SUM(CASE WHEN LT.AllocName='Under11TOIL' THEN RA.Amount ELSE 0 END) AS Under11TOILTaken,	
						SUM(CASE WHEN LT.AllocName='Over12TOIL' THEN RA.Amount ELSE 0 END) AS Over12TOILTaken,
						SUM(CASE WHEN LT.AllocName='Casual' THEN RA.Amount ELSE 0 END) AS CasualTaken,
						SUM(CASE WHEN LT.AllocName='LongService' THEN RA.Amount ELSE 0 END) AS LongServiceTaken,
						SUM(CASE WHEN LT.AllocName='Other' THEN RA.Amount ELSE 0 END) AS OtherTaken,
						SP.UD_UserID as ScheduledPersonID,
						sp.UD_DisplayName as displayname,
						sp.UD_StaffNumber as StaffNumber,
						CASE WHEN MONTH(LAP.ddate) <=3 THEN YEAR(LAP.ddate)-1 ELSE YEAR(LAP.ddate) END AS iyear
				   FROM LeaveApplications LAP 
				  INNER JOIN UserDetails AS sp ON LAP.SchedulingPersonID=SP.UD_UserID 					      
				  INNER JOIN ref_LeaveApplications_Amounts RA ON RA.ApplicationID = LAP.ID
				  INNER JOIN LeaveAllocateTypes LT ON LT.ID=RA.LeaveTypeID
				  WHERE LAP.SchedulingPersonID > 0
					AND LAP.ddate BETWEEN @LeaveYearStartDate AND @LeaveYearEndDate 
					AND LAP.Deleted = 0 
				  GROUP BY CASE WHEN MONTH(LAP.ddate) <=3 THEN YEAR(LAP.ddate)-1 ELSE YEAR(LAP.ddate) END, 
							SP.UD_UserID, 
							sp.UD_DisplayName,
							sp.UD_StaffNumber
			 ) LAP ON LA.ScheduledPersonID = LAP.ScheduledPersonID
			) IMD 
				 
 
			 select Area,
					CostCode,
					[Scheduling Team Name],
					fd.Netlogin,
					[Staff Number],
					[Display Name],	
					EFT,
					round(( case when [Annual Leave (above Carry Over)] > 0 then [Annual Leave (above Carry Over)] else 0 end +
					case when [PHL (above Carry Over)]  > 0 then [PHL (above Carry Over)] else 0 end +
					case when [Additional (above Carry Over)]  > 0 then [Additional (above Carry Over)] else 0 end +
					[EDP TOIL Balance] +
					[Other Balance] ) / 8.75,0) as [Approx. Days to Take],
					[Annual Balance],
					[Annual Leave (above Carry Over)],
					AnnualLeaveCarryOverLimit,
					[PHL Balance],
					[PHL (above Carry Over)],
					PHLCarryOverLimit,
					Additional,
					[Additional (above Carry Over)],
					[EDP TOIL Balance],
					[Under 11 TOIL Balance],
					[Other Balance]
			 from (
			 select dv.DivisionName as Area,
					sd.UC_CostCode CostCode,
					st.schedulingTeamName as [Scheduling Team Name],
					tp.netlogin Netlogin,
					tp.StaffNumber as [Staff Number],
					tp.DisplayName as [Display Name],	
					sd.UC_EFT as EFT,
					--round((AnnualRemaining+PHLRemaining+compRemaining+OtherRemaining-98)/8.75,0) as [Approx. Days to Take], 
					AnnualRemaining as [Annual Balance],
					round(AnnualRemaining - ( 35 * sd.UC_EFT),2) as [Annual Leave (above Carry Over)],
					round(35 * sd.UC_EFT,2) as AnnualLeaveCarryOverLimit,
					PHLRemaining as [PHL Balance],
					round(PHLRemaining -  ( 63 * sd.UC_EFT),2) as [PHL (above Carry Over)],
					round(63 * sd.UC_EFT ,2) as PHLCarryOverLimit,
					AdditionalRemaining as Additional,
					AdditionalRemaining - 280 as [Additional (above Carry Over)],
					compRemaining as [EDP TOIL Balance],
					Under11TOILRemaining as [Under 11 TOIL Balance],
					OtherRemaining as [Other Balance],
					st.schedulingTeamId,
					dv.DivisionID
			  from #Temp TP
			 inner join UserDetails sp on sp.UD_UserID = tp.ScheduledPersonID 
			 left join ScheduledPersonTeam_LINK sl on sl.ScheduledPersonID = sp.UD_UserID 
				   and sl.scheduledType = 1
				   and sl.IsHomeTeam = 1
				   and GETDATE() between sl.StartDate and sl.EndDate
			 left join schedulingTeams st on st.schedulingTeamId = sl.TeamID
			 left join Divisions dv on dv.DivisionID = st.divisionid
			 left join UserConfigs sd on sp.UD_UserID = sd.UC_UserID and getdate() between sd.UC_StartDate and sd.UC_EndDate
			 where  AnnualRemaining <> 0 OR PHLRemaining <> 0 OR compRemaining <> 0 
				OR AdditionalRemaining <> 0 OR Under11TOILRemaining <> 0 
				OR OtherRemaining <> 0 
			 ) fd
			 INNER JOIN 
			  ( select da.UR_UserID as UserId, da.UR_DivisionId as DivisionId
				  from UserRoles da
				 INNER join REF_Roles rr on rr.RoleID = da.UR_RoleID
				 INNER join UserDetails us on us.UD_UserID = da.UR_UserID
				 where us.UD_NetLogin = @NetLogin
				   and rr.RoleName = 'Area Reports'
				 union
				select usl.UR_UserID as UserId, dv.DivisionID 
				  from UserRoles usl 
				 inner join REF_Roles RR on rr.RoleID = usl.UR_RoleID 
				 inner join UserDetails ud on ud.UD_UserID = usl.UR_UserID
				 inner join Divisions dv on 1 =1 
				 where rr.RoleName = 'System Admin'
				   and ud.UD_NetLogin = @NetLogin
			  ) UL on UL.DivisionId = fd.DivisionID
			 where fd.[Scheduling Team Name]  not in ('Archive','TO Archive')
			order by 1,3,6


END